// @ts-ignore
import { Controller } from '@hotwired/stimulus';
import dragula from "dragula";

export default class extends Controller {
  static values = {
    columns: String
  };

  declare readonly columnsValue: string;
  private drake: any;

  connect(): void {
    console.log("🌲 Kanban controller connected.");
    this.createSortableKanban();
  }

  public createSortableKanban(): void {
    var divElements: HTMLDivElement[] = [];
    JSON.parse(this.columnsValue).forEach((column: string) => {
      divElements.push(<HTMLDivElement>document.getElementById(column));
    })

    this.drake = dragula(divElements);

    this.drake.on('drop', async function (element: Element, target: Element, source: Element, sibling: Element) {
      // @ts-ignore
      const transitionId = target.dataset.kanbanTransitionId;
      // @ts-ignore
      const issueId = element.dataset.issueId;

      try {
        const response = await fetch(`/app/issue/${issueId}/transition/${transitionId}`, {
          method: 'POST',
        });

        throw new Error('API error');
        if (!response.ok) {
          throw new Error('API error');
        }

      } catch (error) {
        console.error("❌ Failed to update issue transition:", error);
        this.drake.cancel(true); // Revert the move, `this` is correctly bound now
      }
    }.bind(this));
  }

}

